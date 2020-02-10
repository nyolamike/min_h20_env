<footer class="main-footer ptb-5">
    <!-- To the right -->
    <div class="float-right d-none d-sm-inline">
        <small><?php echo $bag["footer-right-label"]; ?>
            |
            <a  onclick="_ace.on_vm_logout()" class="text-danger pointer">
                <i class="fas fa-power-off"></i>
                Logout
            </a>
        </small>
    </div>
    <!-- Default to the left -->
    <strong>
        <small>Copyright &copy; <?php echo date("yy"); ?>
            <b><?php echo $bag["footer-company-name"]; ?>.</b> All rights reserved.

            - powered by
            <a href="<?php echo $bag["footer-powered-by-link"]; ?>"  ><b><?php echo $bag["footer-powered-by"]; ?></b></a>.
        </small>


    </strong>

</footer>